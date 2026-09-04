import { describe, it, expect } from 'vitest'

import { mount } from '@vue/test-utils'
import FAQ from '../FAQ.vue'

describe('FAQ', () => {
  it('reveals an answer when its question is clicked', async () => {
    const wrapper = mount(FAQ)
    const firstQuestion = wrapper.find('.cursor-pointer')
    const firstAnswer = firstQuestion.find('.mt-2')

    expect(firstAnswer.attributes('style')).toContain('display: none')

    await firstQuestion.trigger('click')

    const revealedAnswer = firstQuestion.find('.mt-2')
    expect(revealedAnswer.attributes('style')).not.toContain('display: none')
    expect(revealedAnswer.text()).toContain('Pour réserver un espace de coworking')
  })
})
